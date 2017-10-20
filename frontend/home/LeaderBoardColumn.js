import React from 'react';
import PropTypes from 'prop-types';
import {Col, Panel} from 'react-bootstrap';
import {Msg} from '../containers';
import {courts, courtsMsg} from '../model';
import LeaderBoard from './LeaderBoard';
import {getTop, getBottom} from './selectors';

const LeaderBoardColumn = ({court}) => (
    <Col sm={4}>
        <Panel header={<p><Msg msg={courtsMsg[court]} /></p>} />
        <LeaderBoard court={court} selector={getTop} type="positive" />
        <LeaderBoard court={court} selector={getBottom} type="negative" />
    </Col>
);

LeaderBoardColumn.propTypes = {
    court: PropTypes.oneOf(Object.values(courts)).isRequired,
};

export default LeaderBoardColumn;
