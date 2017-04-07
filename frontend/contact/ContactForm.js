import React from 'react';
import {RichText} from '../containers';
import {CaptchaForm, TextAreaField, EmailField} from '../containers/form';
import {sendEmail} from './actions';

export default () => (
    <section>
        <RichText msg="contact.us.text" />
        <CaptchaForm form="contact" action={sendEmail} submitLabel="contact.form.submit">
            <EmailField name="email" label="form.email" />
            <TextAreaField name="text" label="contact.form.message" />
        </CaptchaForm>
    </section>
);
