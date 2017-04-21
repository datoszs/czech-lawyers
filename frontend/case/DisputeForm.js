import React from 'react';
import {Panel, Button} from 'react-bootstrap';
import {RichText, Msg} from '../containers';
import {EmailField, TextField, CaptchaForm, TextAreaField, SelectOption, SelectField} from '../containers/form';
import {dispute} from './actions';

export default () => (
    <Panel bsStyle="danger" header={<Msg msg="case.dispute" />}>
        <RichText msg="case.dispute.text" />
        <CaptchaForm form="dispute" action={dispute}>
            <TextField name="full_name" label="form.name" />
            <EmailField name="from" label="form.email" />
            <SelectField name="disputed_tagging" label="case.dispute.reason">
                <SelectOption label="dispute.result" id="case_result" />
                <SelectOption label="dispute.advocate" id="advocate" />
                <SelectOption label="dispute.both" id="both" />
            </SelectField>
            <TextAreaField name="content" label="case.dispute.reason" />
            <Button type="submit" bsStyle="danger"><Msg msg="case.dispute.submit" /></Button>
        </CaptchaForm>
    </Panel>
);
