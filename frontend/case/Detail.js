import React, {PropTypes} from 'react';
import {connect} from 'react-redux';
import {CaseDetail, courtsMsg, resultMsg} from '../model';
import {DetailField, Msg} from '../containers';
import {getDetail} from './selectors';


const DetailComponent = ({caseDetail}) => (
    <div>
        <DetailField msg="case.advocate">{caseDetail && caseDetail.advocateName}</DetailField>
        <DetailField msg="case.court">{caseDetail && <Msg msg={courtsMsg[caseDetail.court]} />}</DetailField>
        <DetailField msg="case.result">{caseDetail && <Msg msg={resultMsg[caseDetail.result]} />}</DetailField>
    </div>
);

DetailComponent.propTypes = {
    caseDetail: PropTypes.instanceOf(CaseDetail),
};

DetailComponent.defaultProps = {
    caseDetail: null,
};

const mapStateToProps = (state) => ({
    caseDetail: getDetail(state),
});

export default connect(mapStateToProps)(DetailComponent);
