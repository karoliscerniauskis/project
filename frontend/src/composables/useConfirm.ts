import { ElMessageBox } from 'element-plus'
import type { ElMessageBoxOptions } from 'element-plus'

export function useConfirm() {
    async function confirm(
        message: string,
        title: string = 'Confirm',
        options?: Partial<ElMessageBoxOptions>
    ): Promise<boolean> {
        try {
            await ElMessageBox.confirm(message, title, {
                confirmButtonText: 'Confirm',
                cancelButtonText: 'Cancel',
                type: 'warning' as const,
                ...options,
            })
            return true
        } catch {
            return false
        }
    }

    async function confirmDelete(itemName: string = 'this item'): Promise<boolean> {
        return confirm(
            `Are you sure you want to delete ${itemName}?`,
            'Confirm Deletion',
            {
                confirmButtonText: 'Delete',
                type: 'error' as const,
            }
        )
    }

    async function confirmAction(
        action: string,
        itemName: string = '',
        type: 'warning' | 'error' | 'info' = 'warning'
    ): Promise<boolean> {
        const message = itemName
            ? `Are you sure you want to ${action} ${itemName}?`
            : `Are you sure you want to ${action}?`

        return confirm(message, `Confirm ${action.charAt(0).toUpperCase() + action.slice(1)}`, {
            confirmButtonText: action.charAt(0).toUpperCase() + action.slice(1),
            type,
        })
    }

    return {
        confirm,
        confirmDelete,
        confirmAction,
    }
}
