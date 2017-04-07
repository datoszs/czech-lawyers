import React from 'react';
import {Panel} from 'react-bootstrap';
import {RichText, Msg} from '../containers';
import {EmailField, CaptchaForm} from '../containers/form';
import {SimpleFormLayout} from '../components';
import {dispute} from './actions';

export default () => (
    <Panel bsStyle="danger" header={<Msg msg="case.dispute" />}>
        <RichText msg="case.dispute.text" />
        <CaptchaForm form="dispute" action={dispute} inline>
            <SimpleFormLayout submit={<Msg msg="case.dispute.submit" />} bsStyle="danger">
                <EmailField name="email" placeholder="form.email" />
            </SimpleFormLayout>
        </CaptchaForm>
    </Panel>
);
