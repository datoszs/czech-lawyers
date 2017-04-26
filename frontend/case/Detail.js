import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {courtsMsg, resultMsg} from '../model';
import {DetailField} from '../containers';
import {transition, wrapEventStop} from '../util';
import translate from '../translate';
import advocate from '../advocate';
import {getDetail} from './selectors';


const DetailComponent = ({advocateName, court, result, handleAdvocate}) => (
    <div>
        <DetailField msg="case.advocate"><a href="" onClick={wrapEventStop(handleAdvocate)} >{advocateName}</a></DetailField>
        <DetailField msg="case.court">{court}</DetailField>
        <DetailField msg="case.result">{result}</DetailField>
    </div>
);

DetailComponent.propTypes = {
    advocateName: PropTypes.string,
    court: PropTypes.string,
    result: PropTypes.string,
    handleAdvocate: PropTypes.func.isRequired,
};

DetailComponent.defaultProps = {
    advocateName: null,
    court: null,
    result: null,
};

const mapStateToProps = (state) => {
    const caseDetail = getDetail(state);
    return ({
        advocateName: caseDetail && caseDetail.advocateName,
        court: caseDetail && translate.getMessage(state, courtsMsg[caseDetail.court]),
        result: caseDetail && translate.getMessage(state, resultMsg[caseDetail.result]),
        advocateId: caseDetail && caseDetail.advocateId,
    });
};

const mapDispatchToProps = () => ({
    goToAdvocate: (id) => () => transition(advocate, {id}),
});

const mergeProps = ({advocateId, ...stateProps}, {goToAdvocate}) => ({
    handleAdvocate: goToAdvocate(advocateId),
    ...stateProps,
});

export default connect(mapStateToProps, mapDispatchToProps, mergeProps)(DetailComponent);
