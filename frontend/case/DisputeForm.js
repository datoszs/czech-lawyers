import React from 'react';
import {Panel, Button, FormGroup} from 'react-bootstrap';
import {RichText, Msg} from '../containers';
import {EmailField, TextField, CaptchaForm, TextAreaField, SelectOption} from '../containers/form';
import {dispute} from './actions';

export default () => (
    <Panel bsStyle="danger" header={<Msg msg="case.dispute" />}>
        <RichText msg="case.dispute.text" />
        <CaptchaForm form="dispute" action={dispute}>
            <TextField name="full_name" label="form.name" />
            <EmailField name="from" label="form.email" />
            <FormGroup>
                <SelectOption label="dispute.result" name="disputed_tagging" id="case_result" />
                <SelectOption label="dispute.advocate" name="disputed_tagging" id="advocate" />
                <SelectOption label="dispute.both" name="disputed_tagging" id="both" />
            </FormGroup>
            <TextAreaField name="content" label="case.dispute.reason" />
            <Button type="submit" bsStyle="danger"><Msg msg="case.dispute.submit" /></Button>
        </CaptchaForm>
    </Panel>
);
