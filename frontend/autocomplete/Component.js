import React, {PropTypes} from 'react';
import {FormControl} from 'react-bootstrap';
import {SearchForm} from '../components';

const AutocompleteComponent = ({value, onChange, onSubmit, msgPlaceholder, msgSearch}) => (
    <SearchForm onSubmit={onSubmit} msgSubmit={msgSearch}>
        <FormControl type="text" onChange={(event) => onChange(event.target.value)} value={value} placeholder={msgPlaceholder} />
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
