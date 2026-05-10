import type { FormItemRule } from 'element-plus'

export const validationRules = {
    required: (message: string = 'This field is required'): FormItemRule => ({
        required: true,
        message,
        trigger: 'blur',
    }),

    email: (): FormItemRule[] => [
        {
            required: true,
            message: 'Email is required',
            trigger: 'blur',
        },
        {
            type: 'email',
            message: 'Please enter a valid email',
            trigger: 'blur',
        },
    ],

    password: (minLength: number = 8): FormItemRule[] => [
        {
            required: true,
            message: 'Password is required',
            trigger: 'blur',
        },
        {
            min: minLength,
            message: `Password must be at least ${minLength} characters`,
            trigger: 'blur',
        },
    ],

    min: (minValue: number, message?: string): FormItemRule => ({
        validator: (_rule, value, callback) => {
            if (value !== null && value !== undefined && value < minValue) {
                callback(new Error(message || `Value must be at least ${minValue}`))
            } else {
                callback()
            }
        },
        trigger: 'blur',
    }),

    positive: (message: string = 'Value must be positive'): FormItemRule => ({
        validator: (_rule, value, callback) => {
            if (value !== null && value !== undefined && value < 1) {
                callback(new Error(message))
            } else {
                callback()
            }
        },
        trigger: 'blur',
    }),

    conditionalRequired: (
        condition: () => boolean,
        message: string = 'This field is required'
    ): FormItemRule => ({
        validator: (_rule, value, callback) => {
            if (condition() && !value) {
                callback(new Error(message))
            } else {
                callback()
            }
        },
        trigger: 'blur',
    }),
}
