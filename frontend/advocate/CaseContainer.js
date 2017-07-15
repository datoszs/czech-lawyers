import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {List} from 'immutable';
import {ThreeColumn} from '../components';
import {getCases} from './selectors';
import CaseDetail from './CaseDetail';

const CaseContainer = ({cases}) => (
    <ThreeColumn>
        {cases.map((id) => <CaseDetail key={id} id={id} />)}
    </ThreeColumn>
);

CaseContainer.propTypes = {
    cases: PropTypes.instanceOf(List).isRequired,
};

const mapStateToProps = (state) => ({
    cases: getCases(state),
});

export default connect(mapStateToProps)(CaseContainer);
