import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {transition} from '../util';
import {CaseDetail as CaseDetailComponent} from '../components';
import translate from '../translate';
import {courtsMsg, resultMsg} from '../model';
import {getCase} from './selectors';
import caseDetail from '../case';

const mapStateToProps = (state, {id}) => {
    const caseObj = getCase(state, id);
    return {
        registry: caseObj.registry,
        court: caseObj.court ? translate.getMessage(state, courtsMsg[caseObj.court]) : null,
        result: caseObj.result ? translate.getMessage(state, resultMsg[caseObj.result]) : null,
    };
};

const mapDispatchToProps = (state, {id}) => ({
    handleDetail: () => transition(caseDetail.ROUTE, {id}),
});

const CaseDetail = connect(mapStateToProps, mapDispatchToProps)(CaseDetailComponent);

CaseDetail.propTypes = {
    id: PropTypes.number.isRequired,
};

export default CaseDetail;
