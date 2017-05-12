import checkIsEmail from 'validator/lib/isEmail';

export const isRequired = (value) => (value ? null : 'form.error.required');

export const isEmail = (value) => (checkIsEmail(value) ? null : 'form.error.email');
