import React from 'react';
import PropTypes from 'prop-types';
import {Form} from 'react-bootstrap';
import {reduxForm, Field} from 'redux-form/immutable';
import Captcha from 'react-google-recaptcha';
import {wrapEventStop} from '../../util';
import {siteKey} from '../../serverAPI';

const CaptchaFormComponent = ({inline, children, handleSubmit}) => {
    let captcha;
    return (
        <Form inline={inline} onSubmit={wrapEventStop(() => captcha.execute())}>
            {children}
            <Field
                name="captcha_token"
                component={({input}) => <Captcha
                    onChange={(value) => {
                        input.onChange(value);
                        handleSubmit();
                    }}
                    sitekey={siteKey}
                    size="invisible"
                    ref={(component) => { captcha = component; }}
                />}
            />
        </Form>
    );
};

CaptchaFormComponent.propTypes = {
    inline: PropTypes.bool,
    children: PropTypes.node.isRequired,
    handleSubmit: PropTypes.func.isRequired,
};

CaptchaFormComponent.defaultProps = {
    inline: false,
};

const onSubmit = (values, dispatch, {action}) => dispatch(action(values));

const CaptchaForm = (reduxForm({onSubmit})(CaptchaFormComponent));

CaptchaForm.propTypes = {
    form: PropTypes.string.isRequired,
    action: PropTypes.func.isRequired,
};

export default CaptchaForm;
