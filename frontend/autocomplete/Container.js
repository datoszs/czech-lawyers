import React from 'react';
import {connect} from 'react-redux';
import AutoComplete from 'react-autocomplete';
import {ListGroupItem, ListGroup, FormControl} from 'react-bootstrap';
import translate from '../translate';
import {SearchForm, SimpleFormLayout} from '../components';
import {goToAdvocate, setQuery, goToSearch} from './actions';
import {getQuery, getItems} from './selectors';

const mapStateToProps = (state) => ({
    value: getQuery(state),
    items: getItems(state),
    msgSearch: translate.getMessage('search.button'),
});

const mapDispatchToProps = (dispatch) => ({
    onChange: (event) => dispatch(setQuery(event.target.value)),
    onSelect: (id) => dispatch(goToAdvocate(id)),
    onSearch: (query) => dispatch(goToSearch(query)),
});

const mergeProps = ({value, items, msgSearch}, {onChange, onSelect, onSearch}) => ({
    value,
    onChange,
    onSelect,
    items: items.toJS(),
    getItemValue: (item) => String(item.id),
    renderItem: (item, isHighlighted) => <ListGroupItem active={isHighlighted}>{item.name}</ListGroupItem>,
    renderMenu: (children, _, style) => <ListGroup style={{...style, position: 'fixed', zIndex: 10}}>{children}</ListGroup>,
    renderInput: ({ref, ...props}) => <SearchForm onSubmit={() => onSearch(value)}><SimpleFormLayout submit={msgSearch} bsStyle="primary"><FormControl inputRef={ref} {...props} /></SimpleFormLayout></SearchForm>,
});

export default connect(mapStateToProps, mapDispatchToProps, mergeProps)(AutoComplete);
