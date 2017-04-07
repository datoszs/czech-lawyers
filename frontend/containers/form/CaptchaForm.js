import React, {PropTypes} from 'react';
import {Form} from 'react-bootstrap';
import {reduxForm, Field} from 'redux-form/immutable';
import Captcha from 'react-google-recaptcha';
import {wrapEventStop} from '../../util';

const CaptchaFormComponent = ({inline, children, handleSubmit}) => {
    let captcha;
    return (
        <Form inline={inline} onSubmit={wrapEventStop(() => captcha.execute())}>
            {children}
            <Field
                name="g-recaptcha-response"
                component={({input}) => <Captcha
                    onChange={(value) => {
                        input.onChange(value);
                        handleSubmit();
                    }}
                    sitekey="6Ldw-BsUAAAAAJ35FtswvO1Ar2B2XrkTgmFXs4P6"
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
