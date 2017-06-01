import React from 'react';
import {RichText} from '../containers';
import formstatus from '../formstatus';
import {CaptchaForm, TextAreaField, EmailField, TextField} from '../containers/form';
import {sendEmail} from './actions';
import {CONTACT_FORM} from './constants';

export default () => (
    <section>
        <RichText msg="contact.us.text" />
        <CaptchaForm form={CONTACT_FORM} action={sendEmail}>
            <TextField name="full_name" label="form.name" required />
            <EmailField name="from" label="form.email" required />
            <TextAreaField name="content" label="contact.form.message" required />
            <formstatus.SuccessContainer formName={CONTACT_FORM} msg="contact.form.success" />
            <formstatus.ErrorContainer formName={CONTACT_FORM} defaultMsg="contact.form.error.default" />
            <formstatus.SubmitButton formName={CONTACT_FORM} msg="contact.form.submit" />
        </CaptchaForm>
    </section>
);
