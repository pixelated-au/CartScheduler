<script setup>
import { Link, router, usePage } from "@inertiajs/vue3";
import { breakpointsTailwind, useBreakpoints } from "@vueuse/core";
import { computed, inject, onBeforeMount, useTemplateRef, onMounted } from "vue";
import DarkMode from "./DarkMode.vue";
import JetApplicationMark from "@/Jetstream/ApplicationMark.vue";

defineEmits(["toggle-dark-mode"]);

const page = usePage();
const route = inject("route");

const logout = () => router.post(route("logout"));

const breakpoints = useBreakpoints(breakpointsTailwind);

let permissions;
const doesUserHaveMenu = computed(() => {
    if (breakpoints.greaterOrEqual("sm").value) {
        return true;
    }

    return !!permissions.canAdmin;
});

const hasUpdate = computed(() => page.props.hasUpdate);

onBeforeMount(() => {
    permissions = page.props.pagePermissions;
});

/**
 * @import {MenuItem} from 'primevue/menuitem';
 * @import {Ref} from 'vue';
 * @type {Ref<MenuItem[]|null>}
 */
const menuItems = computed(() => {
    if (!doesUserHaveMenu.value) {
        return null;
    }
    const items = [{ label: "Dashboard", route: route("dashboard") }];

    if (permissions.canAdmin) {
        const submenu = [
            { label: "Admin Dashboard", route: route("admin.dashboard") },
            { label: "Users", route: route("admin.users.index") },
            { label: "Locations", route: route("admin.locations.index") },
            { label: "Reports", route: route("admin.reports.index") },
        ];

        items.push({
            label: "Administration",
            hasUpdate: hasUpdate,
            submenuicon: "iconify mdi--chevron-down size-4",
            items: submenu,
        });

        if (permissions.canEditSettings) {
            submenu.push({ label: "Settings", route: route("admin.settings"), hasUpdate: hasUpdate });
        }
    }
    return items;
});

/**
 * @type MenuItem[]
 */
const userMenuItems = [];

onMounted(() => {
    userMenuItems.push({ label: "Profile", route: route("profile.show") });
    if (page.props.enableUserAvailability) userMenuItems.push({
        label: "Availability",
        route: route("user.availability"),
    });
    userMenuItems.push({ label: "Logout", command: () => logout() });
});

const userMenu = useTemplateRef("userMenu");

const toggle = (event) => userMenu.value.toggle(event);
</script>

<template>
<PMenubar :model="menuItems"
          :breakpoint="`${breakpointsTailwind.sm}px`"
          class="max-sm:[&_.p-menubar-root-list]:bg-[position:50%_calc(135%)] max-sm:[&_.p-menubar-root-list]:bg-[size:500%_200%] max-sm:[&_.p-menubar-root-list]:bg-[no-repeat] max-sm:[&_.p-menubar-root-list]:inset-x-0 max-sm:[&_.p-menubar-root-list]:mx-3 max-sm:[&_.p-menubar-root-list]:rounded-t-none bg-panel dark:bg-panel-dark">
  <template #start>
    <Link :href="route('dashboard')">
      <JetApplicationMark class="block w-auto h-9" />
    </Link>
    <Link v-if="!doesUserHaveMenu" :href="route('dashboard')" as="button" class="ml-4">
      Dashboard
    </Link>
  </template>

  <template #item="{ item, props, hasSubmenu, root }">
    <div v-if="hasSubmenu" v-bind="props.action" class="flex gap-2 flex-center">
      <span class="flex relative">
        {{ item.label }}
        <span v-if="item.hasUpdate"
              class="flex absolute -top-1 -right-2 justify-center items-center rounded-full bg-warning dark:bg-warning-light size-2" />
      </span>

      <template v-if="hasSubmenu">
        <div v-if="root" class="iconify mdi--chevron-down size-4" />
        <div v-else class="iconify mdi--chevron-right size-4" />
      </template>
    </div>
    <Link v-else
          :href="item.route"
          v-bind="props.action"
          class="flex relative w-full flex-center font-normal !text-inherit cursor-pointer hover:no-underline">
      {{ item.label }}
      <div v-if="item.hasUpdate"
           class="flex absolute -top-1 -right-2 justify-center items-center rounded-full bg-warning dark:bg-warning-light size-2" />
    </Link>
  </template>

  <template #end>
    <div class="flex gap-3 items-center">
      <DarkMode @is-dark-mode="$emit('toggle-dark-mode', $event)" />

      <PButton type="button"
               variant="outlined"
               severity="secondary"
               @click="toggle"
               aria-haspopup="true"
               aria-controls="overlay_menu">
        {{ $page.props.auth.user.name }}
      </PButton>
      <PMenu ref="userMenu" id="overlay_menu" :model="userMenuItems" :popup="true">
        <template #item="{ item, props }">
          <Link v-if="item.route"
                :href="item.route"
                v-bind="props.action"
                as="button"
                class="block w-full text-end">
            {{ item.label }}
          </Link>
          <PButton v-else
                   v-bind="props.action"
                   variant="text"
                   severity="secondary"
                   class="block w-full text-end">
            {{ item.label }}
          </PButton>
        </template>
      </PMenu>
    </div>
  </template>
</PMenubar>
</template>
