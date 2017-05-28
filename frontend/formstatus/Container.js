import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import AlertContainer from './AlertContainer';
import {isVisible} from './selectors';

const FormStatus = ({visible, formName}) => (visible ? <AlertContainer formName={formName} /> : null);

FormStatus.propTypes = {
    visible: PropTypes.bool.isRequired,
    formName: PropTypes.string.isRequired,
};

const mapStateToProps = (state, {formName}) => ({
    visible: isVisible(state, formName),
});

const FormStatusContainer = connect(mapStateToProps)(FormStatus);

FormStatusContainer.propTypes = {
    formName: PropTypes.string.isRequired,
};

export default FormStatusContainer;
