import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {LeaderBoard as LeaderBoardComponent} from '../components';
import LeaderBoardAdvocate from './LeaderBoardAdvocate';

const mapStateToProps = (state, {selector}) => ({
    ids: selector(state),
});

const mergeProps = ({ids}, dispatchProps, {type}) => ({
    children: ids.map((id) => <LeaderBoardAdvocate key={id} id={id} />),
    type,
});

const LeaderBoard = connect(mapStateToProps, undefined, mergeProps)(LeaderBoardComponent);

LeaderBoard.propTypes = {
    selector: PropTypes.func.isRequired,
    type: PropTypes.oneOf(['positive', 'negative']).isRequired,
};

export default LeaderBoard;
