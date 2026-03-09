import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermissions() {
    const page = usePage();

    const userPermissions = computed(() => {
        return page.props.auth?.user?.permissions || [];
    });

    const userRoles = computed(() => {
        return page.props.auth?.user?.roles || [];
    });

    const can = (permission) => {
        if (!permission) return true;

        // Handle multiple permissions (OR logic)
        if (Array.isArray(permission)) {
            return permission.some(p => userPermissions.value.includes(p));
        }

        return userPermissions.value.includes(permission);
    };

    const hasRole = (role) => {
        if (!role) return true;

        if (Array.isArray(role)) {
            return role.some(r => userRoles.value.includes(r));
        }

        return userRoles.value.includes(role);
    };

    return {
        can,
        hasRole,
        userPermissions,
        userRoles
    };
}
