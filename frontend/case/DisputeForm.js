import React from 'react';
import {Panel, Button} from 'react-bootstrap';
import {RichText, Msg} from '../containers';
import {EmailField, CaptchaForm} from '../containers/form';
import {dispute} from './actions';

export default () => (
    <Panel bsStyle="danger" header={<Msg msg="case.dispute" />}>
        <RichText msg="case.dispute.text" />
        <CaptchaForm form="dispute" action={dispute} inline>
            <EmailField name="email" placeholder="form.email" />
            <Button type="submit" bsStyle="danger"><Msg msg="case.dispute.submit" /></Button>
        </CaptchaForm>
    </Panel>
);
