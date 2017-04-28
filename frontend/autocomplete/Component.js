import React from 'react';
import PropTypes from 'prop-types';
import {FormControl} from 'react-bootstrap';
import {SearchForm, SimpleFormLayout} from '../components';

const AutocompleteComponent = ({value, onChange, onSubmit, msgPlaceholder, msgSearch}) => (
    <SearchForm onSubmit={onSubmit}>
        <SimpleFormLayout submit={msgSearch} bsStyle="primary">
            <FormControl type="text" onChange={(event) => onChange(event.target.value)} value={value} placeholder={msgPlaceholder} />
        </SimpleFormLayout>
    </SearchForm>
);

AutocompleteComponent.propTypes = {
    value: PropTypes.string.isRequired,
    onChange: PropTypes.func.isRequired,
    onSubmit: PropTypes.func.isRequired,
    msgSearch: PropTypes.string.isRequired,
    msgPlaceholder: PropTypes.string.isRequired,
};

export default AutocompleteComponent;
