import PropTypes from 'prop-types';
import {ListGroupItem} from 'react-bootstrap';
import {connect} from 'react-redux';
import {getResult, getSelectedItem} from './selectors';
import {setSelection} from './actions';

const mapStateToProps = (state, {id}) => ({
    children: getResult(state, id).name,
    active: id === getSelectedItem(state),
});

const mapDispatchToProps = (dispatch) => ({
    handleSelection: (id) => () => dispatch(setSelection(id)),
});

const mergeProps = (stateProps, {handleSelection}, {id}) => ({
    ...stateProps,
    onMouseOver: handleSelection(id),
});

const AutocompleteItem = connect(mapStateToProps, mapDispatchToProps, mergeProps)(ListGroupItem);

AutocompleteItem.propTypes = {
    id: PropTypes.number.isRequired,
};

export default AutocompleteItem;
