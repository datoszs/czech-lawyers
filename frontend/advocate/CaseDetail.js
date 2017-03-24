import React, {PropTypes} from 'react';
import {connect} from 'react-redux';
import {Row, Col} from 'react-bootstrap';
import {DetailPanel} from '../components';
import translate from '../translate';
import {courtsMsg, resultMsg} from '../model';
import {getCase} from './selectors';

const CaseDetailComponent = ({registry, court, result}) => (
    <DetailPanel
        title={registry}
        footer={
            <Row>
                <Col sm={4}>{court}</Col>
                <Col sm={4}>{result}</Col>
            </Row>
        }
    />
);

CaseDetailComponent.propTypes = {
    registry: PropTypes.string.isRequired,
    court: PropTypes.string,
    result: PropTypes.string,
};

CaseDetailComponent.defaultProps = {
    court: null,
    result: null,
};

const mapStateToProps = (state, {id}) => {
    const caseObj = getCase(state, id);
    return {
        registry: caseObj.registry,
        court: caseObj.court ? translate.getMessage(state, courtsMsg[caseObj.court]) : null,
        result: caseObj.result ? translate.getMessage(state, resultMsg[caseObj.result]) : null,
    };
};

const CaseDetail = connect(mapStateToProps)(CaseDetailComponent);

CaseDetail.propTypes = {
    id: PropTypes.number.isRequired,
};

export default CaseDetail;
