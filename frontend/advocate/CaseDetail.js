import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import moment from 'moment';
import {CaseDetail as CaseDetailComponent} from '../components';
import {CASE_DETAIL} from '../routes';
import router from '../router';
import translate from '../translate';
import {courtsMsg, resultMsg} from '../model';
import {getCase} from './selectors';

const mapStateToProps = (state, {id}) => {
    const caseObj = getCase(state, id);
    const dateFormat = translate.getShortDateFormat(state);
    return {
        registry: caseObj.registry,
        court: caseObj.court ? translate.getMessage(state, courtsMsg[caseObj.court]) : null,
        result: caseObj.result ? translate.getMessage(state, resultMsg[caseObj.result]) : null,
        date: caseObj.decisionDate ? moment(caseObj.decisionDate).format(dateFormat) : null,
        resultName: caseObj.result,
        href: router.getHref(state, CASE_DETAIL, {id}),
    };
};

const mapDispatchToProps = (dispatch, {id}) => ({
    handleDetail: () => dispatch(router.transition(CASE_DETAIL, {id})),
});

const CaseDetail = connect(mapStateToProps, mapDispatchToProps)(CaseDetailComponent);

CaseDetail.propTypes = {
    id: PropTypes.number.isRequired,
};

export default CaseDetail;
