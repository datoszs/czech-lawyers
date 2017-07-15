import React from 'react';
import PropTypes from 'prop-types';
import {Row, Col} from 'react-bootstrap';
import DetailPanel from './DetailPanel';

const CaseDetail = ({registry, court, result, date, handleDetail}) => (
    <DetailPanel
        title={registry}
        footer={
            <Row>
                <Col sm={4}>{court}</Col>
                <Col sm={4}>{result}</Col>
                <Col sm={4}>{date}</Col>
            </Row>
        }
        onClick={handleDetail}
    />
);

CaseDetail.propTypes = {
    registry: PropTypes.string.isRequired,
    court: PropTypes.string,
    result: PropTypes.string,
    date: PropTypes.string,
    handleDetail: PropTypes.func.isRequired,
};

CaseDetail.defaultProps = {
    court: null,
    result: null,
    date: null,
};

export default CaseDetail;
