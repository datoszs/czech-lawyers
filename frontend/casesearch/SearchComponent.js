import React from 'react';
import PropTypes from 'prop-types';
import {Field} from 'redux-form/immutable';
import {SearchForm, SimpleFormLayout} from '../components';
import {SimpleInputText} from '../components/form';

const SearchComponent = ({handleSubmit, msgSearch, msgPlaceholder}) => (
    <SearchForm onSubmit={handleSubmit}>
        <SimpleFormLayout submit={msgSearch} bsStyle="primary">
            <Field name="query" component={SimpleInputText} props={{placeholder: msgPlaceholder}} />
        </SimpleFormLayout>
    </SearchForm>
);

SearchComponent.propTypes = {
    handleSubmit: PropTypes.func.isRequired,
    msgSearch: PropTypes.string.isRequired,
    msgPlaceholder: PropTypes.string.isRequired,
};

export default SearchComponent;
