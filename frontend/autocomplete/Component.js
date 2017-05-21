import React from 'react';
import PropTypes from 'prop-types';
import {SearchForm, SimpleFormLayout, AutocompleteLayout} from '../components';
import AutocompleteList from './AutocompleteList';
import AutocompleteInput from './AutocompleteInput';

const AutocompleteComponent = ({onSubmit, msgSearch}) => (
    <SearchForm onSubmit={onSubmit}>
        <SimpleFormLayout submit={msgSearch} bsStyle="primary">
            <AutocompleteLayout
                input={<AutocompleteInput />}
                list={<AutocompleteList />}
            />
        </SimpleFormLayout>
    </SearchForm>
);

AutocompleteComponent.propTypes = {
    onSubmit: PropTypes.func.isRequired,
    msgSearch: PropTypes.string.isRequired,
};

export default AutocompleteComponent;
