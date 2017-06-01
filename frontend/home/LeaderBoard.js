import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {LeaderBoard as LeaderBoardComponent} from '../components';
import translate from '../translate';
import LeaderBoardAdvocate from './LeaderBoardAdvocate';

const mapStateToProps = (state, {selector, msg}) => ({
    ids: selector(state),
    header: translate.getMessage(state, msg),
});

const mergeProps = ({ids, header}, dispatchProps, {type}) => ({
    children: ids.map((id) => <LeaderBoardAdvocate key={id} id={id} />),
    header,
    type,
});

const LeaderBoard = connect(mapStateToProps, undefined, mergeProps)(LeaderBoardComponent);

LeaderBoard.propTypes = {
    selector: PropTypes.func.isRequired,
    msg: PropTypes.string.isRequired,
    type: PropTypes.oneOf(['positive', 'negative']).isRequired,
};

export default LeaderBoard;
