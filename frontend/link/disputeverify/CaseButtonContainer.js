import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {getCaseId} from './selectors';
import CaseButton from './CaseButton';

const CaseButtonContainer = ({caseId}) => !!caseId && <CaseButton id={caseId} />;

CaseButtonContainer.propTypes = {
    caseId: PropTypes.string,
};

CaseButtonContainer.defaultProps = {
    caseId: null,
};

const mapStateToProps = (state) => ({
    caseId: getCaseId(state),
});

export default connect(mapStateToProps)(CaseButtonContainer);

