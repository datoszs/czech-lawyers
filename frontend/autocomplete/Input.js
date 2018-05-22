import React from 'react';
import PropTypes from 'prop-types';
import {FormControl} from 'react-bootstrap';
import {SearchForm, SimpleFormLayout} from '../components';

const Input = ({ref, onSearch, msgSearch, ...props}) => (
    <SearchForm onSubmit={onSearch}>
        <SimpleFormLayout submit={msgSearch} bsStyle="primary">
            <FormControl inputRef={ref} {...props} />
        </SimpleFormLayout>
    </SearchForm>
);

Input.propTypes = {
    ref: PropTypes.func.isRequired,
    onSearch: PropTypes.func.isRequired,
    msgSearch: PropTypes.string.isRequired,
};

export default Input;
