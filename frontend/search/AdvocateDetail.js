import React, {PropTypes} from 'react';
import {Panel, Row, Col, Button} from 'react-bootstrap';
import {Statistics} from '../components';

const AdvocateDetail = ({name, IC, city, status, positive, negative, neutral}) => {
    const label = {
        active: 'Aktivní',
        suspended: 'Pozastavený',
        removed: 'Vyškrtnutý',
    }[status];

    const footer = (
        <Row>
            <Col md={4}><b>{city}</b></Col>
            <Col md={4}>IČ <b>{IC}</b></Col>
            <Col md={4}><b>{label}</b></Col>
        </Row>
    );

    return (
        <Panel style={{cursor: 'pointer'}} footer={footer}>
            <Row>
                <Col md={8}>
                    <h2>{name}</h2>
                </Col>
                <Col md={4}>
                    <h1><Statistics positive={positive} negative={negative} neutral={neutral} /></h1>
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
