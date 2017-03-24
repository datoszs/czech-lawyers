import React, {PropTypes} from 'react';
import {connect} from 'react-redux';
import {Row, Col} from 'react-bootstrap';
import {transition} from '../util';
import {DetailPanel} from '../components';
import translate from '../translate';
import {courtsMsg, resultMsg} from '../model';
import {getCase} from './selectors';
import caseDetail from '../case';

const CaseDetailComponent = ({registry, court, result, handleDetail}) => (
    <DetailPanel
        title={registry}
        footer={
            <Row>
                <Col sm={4}>{court}</Col>
                <Col sm={4}>{result}</Col>
            </Row>
        }
        onClick={handleDetail}
    />
);

CaseDetailComponent.propTypes = {
    registry: PropTypes.string.isRequired,
    court: PropTypes.string,
    result: PropTypes.string,
    handleDetail: PropTypes.func.isRequired,
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

const mapDispatchToProps = (state, {id}) => ({
    handleDetail: () => transition(caseDetail, {id}),
});

const CaseDetail = connect(mapStateToProps, mapDispatchToProps)(CaseDetailComponent);

CaseDetail.propTypes = {
    id: PropTypes.number.isRequired,
};

export default CaseDetail;
