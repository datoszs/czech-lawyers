import {NAME} from './constants';

export const SEND_EMAIL = `${NAME}/SEND_EMAIL`;

export const sendEmail = (values) => ({
    type: SEND_EMAIL,
    values,
});
