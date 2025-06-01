<template>
  <div class="flex flex-col items-center pt-6 min-h-screen sm:justify-center sm:pt-0 bg-page dark:bg-page-dark">
    <div>
      <slot name="logo"/>
    </div>

    <div class="overflow-hidden px-6 py-4 mt-6 w-full shadow-md sm:max-w-md sm:rounded-lg bg-panel dark:bg-panel-dark">
      <slot/>
    </div>
  </div>
</template>
