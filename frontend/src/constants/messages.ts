export const MESSAGES = {
    // Success messages
    SUCCESS: {
        VOUCHER_CREATED: 'Voucher created successfully!',
        VOUCHER_CLAIMED: 'Voucher claimed successfully',
        VOUCHER_USED: 'Voucher used successfully',
        VOUCHER_TRANSFERRED: 'Voucher transferred successfully',
        VOUCHER_DEACTIVATED: 'Voucher deactivated successfully',

        PROVIDER_CREATED: 'Provider created successfully!',
        PROVIDER_APPROVED: 'Provider approved successfully',
        PROVIDER_DEACTIVATED: 'Provider deactivated successfully',

        USER_INVITED: 'User invitation sent successfully',
        USER_REMOVED: 'User removed successfully',
        INVITATION_CANCELLED: 'Invitation cancelled successfully',

        SETTINGS_SAVED: 'Settings saved successfully!',
        EMAIL_CHANGED: 'Verification email sent! Logging out...',
        PASSWORD_CHANGED: 'Password changed successfully!',

        EMAIL_BREACH_ENABLED: 'Email breach monitoring enabled',
        EMAIL_BREACH_DISABLED: 'Email breach monitoring disabled',

        REMINDER_SETTINGS_SAVED: 'Reminder settings saved successfully!',

        VOUCHER_VALIDATED: 'Voucher is valid',

        NOTIFICATION_MARKED_READ: 'Notification marked as read',
    },

    // Error messages
    ERROR: {
        VOUCHER_CREATE: 'Failed to create voucher',
        VOUCHER_CLAIM: 'Failed to claim voucher',
        VOUCHER_USE: 'Failed to use voucher',
        VOUCHER_TRANSFER: 'Failed to transfer voucher',
        VOUCHER_DEACTIVATE: 'Failed to deactivate voucher',
        VOUCHER_LOAD: 'Failed to load vouchers',
        VOUCHER_VALIDATE: 'Failed to validate voucher',

        PROVIDER_CREATE: 'Failed to create provider',
        PROVIDER_LOAD: 'Failed to load provider',
        PROVIDER_APPROVE: 'Failed to approve provider',
        PROVIDER_DEACTIVATE: 'Failed to deactivate provider',

        USER_INVITE: 'Failed to invite user',
        USER_REMOVE: 'Failed to remove user',
        USER_LOAD: 'Failed to load users',

        INVITATION_LOAD: 'Failed to load invitations',
        INVITATION_CANCEL: 'Failed to cancel invitation',

        SETTINGS_SAVE: 'Failed to save settings',
        EMAIL_CHANGE: 'Failed to change email',
        PASSWORD_CHANGE: 'Failed to change password',

        EMAIL_BREACH_SETTINGS: 'Failed to update email breach settings',

        REMINDER_SETTINGS_SAVE: 'Failed to save reminder settings',

        NOTIFICATION_LOAD: 'Failed to load notifications',
        NOTIFICATION_MARK_READ: 'Failed to mark notification as read',
        NOTIFICATION_COUNT: 'Failed to fetch notification count',

        GENERIC: 'An error occurred',
    },

    // Confirmation messages
    CONFIRM: {
        VOUCHER_DEACTIVATE: (code: string) => `Are you sure you want to deactivate voucher ${code}?`,
        VOUCHER_CLAIM: 'Are you sure you want to claim this voucher?',

        PROVIDER_APPROVE: (name: string) => `Are you sure you want to approve provider "${name}"?`,
        PROVIDER_DEACTIVATE: (name: string) => `Are you sure you want to deactivate provider "${name}"?`,

        USER_REMOVE: (email: string) => `Are you sure you want to remove user ${email}?`,
        INVITATION_CANCEL: (email: string) => `Are you sure you want to cancel the invitation to ${email}?`,
    },

    // Info messages
    INFO: {
        NO_VOUCHERS: 'No vouchers found',
        NO_PROVIDERS: 'No providers found',
        NO_USERS: 'No users found',
        NO_INVITATIONS: 'No pending invitations',
        NO_NOTIFICATIONS: 'No notifications',
        VOUCHER_INVALID: 'Voucher is invalid',
    },
} as const
