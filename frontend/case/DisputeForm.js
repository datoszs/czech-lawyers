import React from 'react';
import {Panel, Button} from 'react-bootstrap';
import {RichText, Msg} from '../containers';
import {EmailField, TextField, CaptchaForm, TextAreaField} from '../containers/form';
import {dispute} from './actions';

export default () => (
    <Panel bsStyle="danger" header={<Msg msg="case.dispute" />}>
        <RichText msg="case.dispute.text" />
        <CaptchaForm form="dispute" action={dispute}>
            <TextField name="full_name" label="form.name" />
            <EmailField name="from" label="form.email" />
            <TextAreaField name="content" label="case.dispute.reason" />
            <Button type="submit" bsStyle="danger"><Msg msg="case.dispute.submit" /></Button>
        </CaptchaForm>
    </Panel>
);
