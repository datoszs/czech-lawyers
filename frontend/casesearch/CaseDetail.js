import {connect} from 'react-redux';
import PropTypes from 'prop-types';
import {CaseDetail as CaseDetailComponent} from '../components';
import {transition} from '../util';
import {courtsMsg, resultMsg} from '../model';
import translate from '../translate';
import caseDetail from '../case';
import {search} from './modules';

const mapStateToProps = (state, {id}) => {
    const caseObj = search.getResult(state, id);
    return {
        registry: caseObj.registry,
        court: caseObj.court ? translate.getMessage(state, courtsMsg[caseObj.court]) : null,
        result: caseObj.result ? translate.getMessage(state, resultMsg[caseObj.result]) : null,
    };
};

const mapDispatchToProps = (dispatch, {id}) => ({
    handleDetail: () => transition(caseDetail.ROUTE, {id}),
});

const CaseDetail = connect(mapStateToProps, mapDispatchToProps)(CaseDetailComponent);

CaseDetail.propTypes = {
    id: PropTypes.number.isRequired,
};

export default CaseDetail;
