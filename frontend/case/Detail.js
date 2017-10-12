import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import moment from 'moment';
import {courtsMsg, resultMsg} from '../model';
import {DetailField, RouterLink} from '../containers';
import translate from '../translate';
import {ADVOCATE_DETAIL} from '../routes';
import {getDetail} from './selectors';


const DetailComponent = ({advocateId, advocateName, court, result, decisionDate, propositionDate}) => (
    <div>
        <DetailField msg="case.advocate">
            <RouterLink route={ADVOCATE_DETAIL} params={{id: advocateId}}>{advocateName}</RouterLink>
        </DetailField>
        <DetailField msg="case.court">{court}</DetailField>
        <DetailField msg="case.result">{result}</DetailField>
        <DetailField msg="case.date.proposition">{propositionDate}</DetailField>
        <DetailField msg="case.date.decision">{decisionDate}</DetailField>
    </div>
);

DetailComponent.propTypes = {
    advocateId: PropTypes.number,
    advocateName: PropTypes.string,
    court: PropTypes.string,
    result: PropTypes.string,
    decisionDate: PropTypes.string,
    propositionDate: PropTypes.string,
};

DetailComponent.defaultProps = {
    advocateId: null,
    advocateName: null,
    court: null,
    result: null,
    decisionDate: null,
    propositionDate: null,
};

const mapStateToProps = (state) => {
    const caseDetail = getDetail(state);
    const dateFormat = translate.getShortDateFormat(state);
    return ({
        advocateName: caseDetail && caseDetail.advocateName,
        court: caseDetail && translate.getMessage(state, courtsMsg[caseDetail.court]),
        result: caseDetail && translate.getMessage(state, resultMsg[caseDetail.result]),
        advocateId: caseDetail && caseDetail.advocateId,
        decisionDate: caseDetail && caseDetail.decisionDate && moment(caseDetail.decisionDate).format(dateFormat),
        propositionDate: caseDetail && caseDetail.propositionDate && moment(caseDetail.propositionDate).format(dateFormat),
    });
};

export default connect(mapStateToProps)(DetailComponent);
