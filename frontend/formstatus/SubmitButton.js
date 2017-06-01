import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {SubmitButton as SubmitButtonComponent} from '../components/form';
import translate from '../translate';
import {isSubmitting} from './selectors';

const mapStateToPops = (state, {formName, msg}) => ({
    children: translate.getMessage(state, msg),
    submitting: isSubmitting(state, formName),
});

const SubmitButton = connect(mapStateToPops)(SubmitButtonComponent);

SubmitButton.propTypes = {
    formName: PropTypes.string.isRequired,
    msg: PropTypes.string.isRequired,
};

export default SubmitButton;

