import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import moment from 'moment';
import {courtsMsg, resultMsg} from '../model';
import {DetailField} from '../containers';
import {wrapEventStop} from '../util';
import translate from '../translate';
import router from '../router';
import {ADVOCATE_DETAIL} from '../routes';
import {getDetail} from './selectors';


const DetailComponent = ({advocateName, court, result, decisionDate, propositionDate, handleAdvocate}) => (
    <div>
        <DetailField msg="case.advocate"><a href="" onClick={wrapEventStop(handleAdvocate)} >{advocateName}</a></DetailField>
        <DetailField msg="case.court">{court}</DetailField>
        <DetailField msg="case.result">{result}</DetailField>
        <DetailField msg="case.date.proposition">{propositionDate}</DetailField>
        <DetailField msg="case.date.decision">{decisionDate}</DetailField>
    </div>
);

DetailComponent.propTypes = {
    advocateName: PropTypes.string,
    court: PropTypes.string,
    result: PropTypes.string,
    decisionDate: PropTypes.string,
    propositionDate: PropTypes.string,
    handleAdvocate: PropTypes.func.isRequired,
};

DetailComponent.defaultProps = {
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

const mapDispatchToProps = (dispatch) => ({
    goToAdvocate: (id) => () => dispatch(router.transition(ADVOCATE_DETAIL, {id})),
});

const mergeProps = ({advocateId, ...stateProps}, {goToAdvocate}) => ({
    handleAdvocate: goToAdvocate(advocateId),
    ...stateProps,
});

export default connect(mapStateToProps, mapDispatchToProps, mergeProps)(DetailComponent);
