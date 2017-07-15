import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {getName, getResidence} from './selectors';

const SameNameAdvocateComponent = ({name, residence}) => <span>{name}{residence && <span> ({residence})</span>}</span>;

const mapStateToProps = (state, {id}) => ({
    name: getName(state, id),
    residence: getResidence(state, id),
});

SameNameAdvocateComponent.propTypes = {
    name: PropTypes.string.isRequired,
    residence: PropTypes.string,
};

SameNameAdvocateComponent.defaultProps = {
    residence: null,
};

const SameNameAdvocate = connect(mapStateToProps)(SameNameAdvocateComponent);

SameNameAdvocate.propTypes = {
    id: PropTypes.number.isRequired,
};

export default SameNameAdvocate;
