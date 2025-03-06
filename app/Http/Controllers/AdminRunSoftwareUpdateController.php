<?php

namespace App\Http\Controllers;

use App\Lib\FilterAnsiEscapeSequencesStreamedOutput;
use App\Settings\GeneralSettings;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;
use Log;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class AdminRunSoftwareUpdateController extends Controller
{
    public function __construct(
        private readonly GeneralSettings $settings,
    ) {
    }

    public function __invoke()
    {
        // Ensure any existing output buffers are cleared
        if (ob_get_level()) {
            ob_clean();
        }
        $available = $this->settings->availableVersion;
        $params    = ['--force' => true, '--install-version' => $available];

        return Response::stream(
            callback: static function () use ($params) {
                try {
                    // Create stream
                    $stream = fopen('php://output', 'wb');

                    $output = App::make(
                        abstract: FilterAnsiEscapeSequencesStreamedOutput::class,
                        parameters: [
                            'stream'    => $stream,
                            'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
                            'decorated' => true,
                        ]
                    );
                    // Call your command
                    $exitCode = Artisan::call('streamline:run-update', $params, $output);

                    if ($exitCode !== 0) {
                        throw new RuntimeException("Command failed");
                    }

                    fclose($stream);
                } catch (Exception $e) {

                    // Log the error
                    Log::error('Command streaming failed: ' . $e->getMessage());

                    // Output error message to stream
                    echo "Error: " . $e->getMessage();

                    // Ensure stream is closed
                    if (isset($stream) && is_resource($stream)) {
                        fclose($stream);
                    }
                }
            },
            headers: [
                'Content-Type'      => 'text/plain',
                'X-Accel-Buffering' => 'no',
                'Cache-Control'     => 'no-cache',
            ]);
    }
}
