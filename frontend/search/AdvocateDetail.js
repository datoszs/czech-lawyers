import React, {PropTypes} from 'react';
import {Panel, Button, Label, Row, Col, Glyphicon} from 'react-bootstrap';
import Statistics from './Statistics';

const AdvocateDetail = ({name, IC, city, status, positive, negative, neutral}) => {
    const bsStyle = {
        active: 'success',
        suspended: 'warning',
        removed: 'error,',
    }[status];
    const label = {
        active: 'Aktivní',
        suspended: 'Pozastavený',
        removed: 'Vyškrtnutý',
    }[status];

    const footer = (
        <Row>
            <Col md={3}><h5><b>Sídlo</b></h5>{city}</Col>
            <Col md={3}><h5><b>IČ</b></h5>{IC}</Col>
            <Col md={3}><h5><b>Status</b></h5><Label bsStyle={bsStyle}>{label}</Label></Col>
            <Col md={2}><Button>Více &gt;</Button></Col>
        </Row>
    );

    return (
        <Panel bsStyle={bsStyle} style={{cursor: 'pointer'}} footer={footer}>
            <Row>
                <Col md={8}>
                    <h2 style={{display: 'inline-block'}}>{name}</h2>
                </Col>
                <Col md={4}>
                    <Statistics number={positive} scale={1.2} color="green" />
                    <Statistics number={negative} scale={0.8} color="red" />
                    <Statistics number={neutral} scale={0.5} color="gray" />
                    <Glyphicon glyph="question-sign" />
                </Col>


            </Row>
        </Panel>
    );
};

AdvocateDetail.propTypes = {
    name: PropTypes.string.isRequired,
    IC: PropTypes.string.isRequired,
    city: PropTypes.string.isRequired,
    status: PropTypes.string.isRequired,
    positive: PropTypes.number.isRequired,
    negative: PropTypes.number.isRequired,
    neutral: PropTypes.number.isRequired,
};

export default AdvocateDetail;
