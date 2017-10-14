import React from 'react';
import PropTypes from 'prop-types';
import {Row, Col} from 'react-bootstrap';
import {Result} from './result';
import DetailPanel from './DetailPanel';
import styles from './CaseDetail.less';

const CaseDetail = ({registry, court, result, resultName, date, handleDetail, href}) => (
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
        href={href}
    >
        {resultName && <span className={styles.result}><Result result={resultName} /></span>}
    </DetailPanel>
);

CaseDetail.propTypes = {
    registry: PropTypes.string.isRequired,
    court: PropTypes.string,
    result: PropTypes.string,
    date: PropTypes.string,
    handleDetail: PropTypes.func.isRequired,
    resultName: PropTypes.oneOf(['positive', 'negative', 'neutral']),
    href: PropTypes.string.isRequired,
};

CaseDetail.defaultProps = {
    court: null,
    result: null,
    date: null,
    resultName: null,
};

export default CaseDetail;
