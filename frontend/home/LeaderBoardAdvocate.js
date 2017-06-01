import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {ListGroupItem} from 'react-bootstrap';
import {transition} from '../util';
import advocate from '../advocate';
import {getName} from './selectors';

const mapStateToProps = (state, {id}) => ({
    children: getName(state, id),
});

const mergeProps = ({children}, dispatchProps, {id}) => ({
    children,
    onClick: () => transition(advocate.ROUTE, {id}),
});

const LeaderBoardAdvocate = connect(mapStateToProps, undefined, mergeProps)(ListGroupItem);

LeaderBoardAdvocate.propTypes = {
    id: PropTypes.number.isRequired,
};

export default LeaderBoardAdvocate;
