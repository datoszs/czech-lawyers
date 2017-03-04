import React, {PropTypes} from 'react';
import {Panel, Label, Row, Col} from 'react-bootstrap';
import Statistics from './Statistics';

const AdvocateDetail = ({name, IC, city, status, positive, negative, neutral}) => {
    const bsStyle = {
        active: 'success',
        suspended: 'warning',
        removed: 'danger',
    }[status];
    const label = {
        active: 'Aktivní',
        suspended: 'Pozastavený',
        removed: 'Vyškrtnutý',
    }[status];

    const footer = (
        <Row>
            <Col md={4}><b>{city}</b></Col>
            <Col md={4}>IČ <b>{IC}</b></Col>
        </Row>
    );

    return (
        <Panel bsStyle={bsStyle} style={{cursor: 'pointer'}} footer={footer}>
            <Row>
                <Col md={8}>
                    <h2>{name}</h2>
                    <h4><Label bsStyle={bsStyle}>{label}</Label></h4>
                </Col>
                <Col md={4}>
                    <br /><br />
                    <Statistics number={positive} scale={1.6} color="green" />
                    <Statistics number={negative} scale={1.2} color="red" />
                    <Statistics number={neutral} scale={1} color="gray" />
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
