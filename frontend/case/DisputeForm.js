import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Panel} from 'react-bootstrap';
import {RichText, Msg} from '../containers';
import {EmailField, TextField, CaptchaForm, TextAreaField, SelectOption, SelectField} from '../containers/form';
import formstatus from '../formstatus';
import {FORM} from './constants';
import {dispute} from './actions';
import {getDetail} from './selectors';

const DisputeForm = ({advocateFinal, resultFinal}) => (
    <Panel bsStyle="danger" header={<Msg msg="case.dispute" />}>
        <RichText msg="case.dispute.text" />
        <CaptchaForm form={FORM} action={dispute}>
            <TextField name="full_name" label="form.name" required />
            <EmailField name="from" label="form.email" required />
            <SelectField name="disputed_tagging" label="case.dispute.reason" required>
                {resultFinal || <SelectOption label="case.dispute.reason.result" id="case_result" />}
                {advocateFinal || <SelectOption label="case.dispute.reason.advocate" id="advocate" />}
                {resultFinal || advocateFinal || <SelectOption label="case.dispute.reason.both" id="both" />}
                {advocateFinal && <RichText msg="case.dispute.final.advocate" />}
                {resultFinal && <RichText msg="case.dispute.final.result" />}
            </SelectField>
            <TextAreaField name="content" label="case.dispute.comment" required />
            <formstatus.ErrorContainer
                formName={FORM}
                defaultMsg="case.dispute.error.default"
                errorMap={{inconsistent: 'case.dispute.error.inconsistent'}}
            />
            <formstatus.SubmitButton bsStyle="danger" msg="case.dispute.submit" formName={FORM} />
        </CaptchaForm>
    </Panel>
);

DisputeForm.propTypes = {
    advocateFinal: PropTypes.bool.isRequired,
    resultFinal: PropTypes.bool.isRequired,
};

const mapStateToProps = (state) => {
    const detail = getDetail(state);
    return {
        advocateFinal: detail.advocateFinal,
        resultFinal: detail.resultFinal,
    };
};

export default connect(mapStateToProps)(DisputeForm);
