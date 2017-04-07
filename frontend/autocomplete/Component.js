import React, {PropTypes} from 'react';
import {FormControl} from 'react-bootstrap';
import {Msg} from '../containers';
import {SearchForm, SimpleFormLayout} from '../components';

const AutocompleteComponent = ({value, onChange, onSubmit, msgPlaceholder, msgSearch}) => (
    <SearchForm onSubmit={onSubmit} msgSubmit={msgSearch}>
        <SimpleFormLayout submit={<Msg msg="search.button" />} bsStyle="primary">
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
