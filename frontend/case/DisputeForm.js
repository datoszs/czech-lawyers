import React from 'react';
import {Panel} from 'react-bootstrap';
import {RichText, Msg} from '../containers';
import {EmailField, CaptchaForm} from '../containers/form';
import {dispute} from './actions';

export default () => (
    <Panel bsStyle="danger" header={<Msg msg="case.dispute" />}>
        <RichText msg="case.dispute.text" />
        <CaptchaForm form="dispute" action={dispute} submitLabel="case.dispute.submit" inline>
            <EmailField name="email" placeholder="form.email" />
        </CaptchaForm>
    </Panel>
);
