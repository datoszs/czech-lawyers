import React from 'react';
import {Panel} from 'react-bootstrap';
import {RichText} from '../containers';
import {EmailField, CaptchaForm} from '../containers/form';
import {dispute} from './actions';

export default () => (
    <Panel>
        <RichText msg="case.dispute" />
        <CaptchaForm form="dispute" action={dispute} submitLabel="case.dispute.submit" inline>
            <EmailField name="email" placeholder="form.email" />
        </CaptchaForm>
    </Panel>
);
