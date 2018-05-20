import React from 'react';
import {connect} from 'react-redux';
import AutoComplete from 'react-autocomplete';
import {ListGroupItem, ListGroup} from 'react-bootstrap';
import translate from '../translate';
import {goToAdvocate, setQuery, goToSearch} from './actions';
import {getQuery, getItems} from './selectors';
import Input from './Input';

const mapStateToProps = (state) => ({
    query: getQuery(state),
    items: getItems(state),
    msgSearch: translate.getMessage(state, 'search.button'),
});

const mapDispatchToProps = (dispatch) => ({
    onChange: (event) => dispatch(setQuery(event.target.value)),
    onSelect: (id) => dispatch(goToAdvocate(id)),
    onSearch: (query) => dispatch(goToSearch(query)),
});

const mergeProps = ({query, items, msgSearch}, {onChange, onSelect, onSearch}) => ({
    value: query,
    onChange,
    onSelect,
    items: items.toJS(),
    getItemValue: (item) => String(item.id),
    renderItem: (item, isHighlighted) => <ListGroupItem active={isHighlighted}>{item.name}</ListGroupItem>,
    renderMenu: (children, _, style) => <ListGroup style={{...style, position: 'fixed', zIndex: 10}}>{children}</ListGroup>,
    renderInput: Input,
    inputProps: {msgSearch, onSearch: () => onSearch(query)},
});

export default connect(mapStateToProps, mapDispatchToProps, mergeProps)(AutoComplete);
