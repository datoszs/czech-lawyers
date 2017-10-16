import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {ListGroupItem} from 'react-bootstrap';
import {wrapLinkMouseEvent} from '../util';
import router from '../router';
import {ADVOCATE_DETAIL} from '../routes';
import {getName} from './selectors';

const mapStateToProps = (state, {id}) => ({
    children: getName(state, id),
    href: router.getHref(state, ADVOCATE_DETAIL, {id}),
});

const mapDispatchToProps = (dispatch, {id}) => ({
    onClick: wrapLinkMouseEvent(() => dispatch(router.transition(ADVOCATE_DETAIL, {id}))),
});

const LeaderBoardAdvocate = connect(mapStateToProps, mapDispatchToProps)(ListGroupItem);

LeaderBoardAdvocate.propTypes = {
    id: PropTypes.number.isRequired,
};

export default LeaderBoardAdvocate;
