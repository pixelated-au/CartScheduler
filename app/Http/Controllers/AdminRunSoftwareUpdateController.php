<?php

namespace App\Http\Controllers;

use App\Lib\FilterAnsiEscapeSequencesStreamedOutput;
use App\Settings\GeneralSettings;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Log;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class AdminRunSoftwareUpdateController extends Controller
{
    public function __construct(private readonly GeneralSettings $settings)
    {
    }

    public function __invoke()
    {
        // Ensure any existing output buffers are cleared
        if (ob_get_level()) {
            ob_end_clean();
        }
        $available = $this->settings->availableVersion;
        $params    = ['--force' => true];
        if (Str::endsWith($available, 'b')) {
            $params['--beta'] = true;
        }

        return Response::stream(
            callback: static function () use ($params) {
                try {
                    // Create stream
                    $stream = fopen('php://output', 'wb');

                    if ($stream === false) {
                        throw new RuntimeException('Could not open output stream');
                    }

                    // Create StreamOutput with verbose output
                    $output = new FilterAnsiEscapeSequencesStreamedOutput(
                        $stream,
                        OutputInterface::VERBOSITY_VERBOSE,
                        true // Enable decoration (colors and formatting)
                    );

                    // Call your command
                    $exitCode = Artisan::call('streamline:run-update', $params, $output);

                    if ($exitCode !== 0) {
                        throw new RuntimeException("Command failed");
                    }

                    fclose($stream);
                } catch (Exception $e) {

                    // Log the error
                    Log::error('Command streaming failed: '.$e->getMessage());

                    // Output error message to stream
                    echo "Error: ".$e->getMessage();

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
