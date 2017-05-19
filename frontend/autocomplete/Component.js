import React, {Component} from 'react';
import PropTypes from 'prop-types';
import {SearchForm, SimpleFormLayout, AutocompleteLayout} from '../components';
import AutocompleteList from './AutocompleteList';
import AutocompleteInput from './AutocompleteInput';

class AutocompleteComponent extends Component {
    componentWillUnmount() {
        this.props.hide();
    }

    render() {
        return (
            <SearchForm onSubmit={this.props.onSubmit}>
                <SimpleFormLayout submit={this.props.msgSearch} bsStyle="primary">
                    <AutocompleteLayout
                        input={<AutocompleteInput />}
                        list={<AutocompleteList />}
                    />
                </SimpleFormLayout>
            </SearchForm>
        );
    }
}

AutocompleteComponent.propTypes = {
    onSubmit: PropTypes.func.isRequired,
    hide: PropTypes.func.isRequired,
    msgSearch: PropTypes.string.isRequired,
};

export default AutocompleteComponent;
