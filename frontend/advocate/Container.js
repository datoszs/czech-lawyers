import React, {PropTypes} from 'react';
import {connect} from 'react-redux';
import {PageHeader} from 'react-bootstrap';
import {AdvocateDetail} from '../model';
import {getAdvocate} from './selectors';

const AdvocateContainer = ({advocate}) => (
    <section>
        <PageHeader>{advocate ? advocate.name : 'Detail advokáta'}</PageHeader>
    </section>
);

AdvocateContainer.defaultProps = {
    advocate: null,
};

AdvocateContainer.propTypes = {
    advocate: PropTypes.instanceOf(AdvocateDetail),
};

const mapStateToProps = (state) => ({
    advocate: getAdvocate(state),
});

export default connect(mapStateToProps)(AdvocateContainer);
