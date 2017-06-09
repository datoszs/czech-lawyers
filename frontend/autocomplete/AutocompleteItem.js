import PropTypes from 'prop-types';
import {ListGroupItem} from 'react-bootstrap';
import {connect} from 'react-redux';
import {getResult, getSelectedItem} from './selectors';
import {setSelection, setAdvocate} from './actions';

const mapStateToProps = (state, {id}) => ({
    children: getResult(state, id).name,
    active: id === getSelectedItem(state),
});

const mapDispatchToProps = (dispatch, {id}) => ({
    onMouseOver: () => dispatch(setSelection(id)),
    onClick: () => dispatch(setAdvocate(id)),
});

const AutocompleteItem = connect(mapStateToProps, mapDispatchToProps)(ListGroupItem);

AutocompleteItem.propTypes = {
    id: PropTypes.number.isRequired,
};

export default AutocompleteItem;
