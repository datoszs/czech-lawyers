import React from 'react';
import {connect} from 'react-redux';
import AutoComplete from 'react-autocomplete';
import {goToAdvocate, setQuery} from './actions';
import {getQuery, getItems} from './selectors';

const mapStateToProps = (state) => ({
    value: getQuery(state),
    items: getItems(state),
});

const mapDispatchToProps = (dispatch) => ({
    onChange: (event) => dispatch(setQuery(event.target.value)),
    onSelect: (id) => dispatch(goToAdvocate(id)),
});

const mergeProps = ({value, items}, {onChange, onSelect}) => ({
    value,
    onChange,
    onSelect,
    items: items.toJS(),
    getItemValue: (item) => String(item.id),
    renderItem: (item) => <div>{item.name}</div>,
});

export default connect(mapStateToProps, mapDispatchToProps, mergeProps)(AutoComplete);
