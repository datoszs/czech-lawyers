import {connect} from 'react-redux';
import {Button} from 'react-bootstrap';
import {If, transition} from '../../util';
import translate from '../../translate';
import caseDetail from '../../case';
import {getCaseId, isLoading} from './selectors';

const mapStateToProps = (state) => ({
    caseId: getCaseId(state),
    children: translate.getMessage(state, 'case.dispute.verify.case'),
    disabled: isLoading(state),
});

const mergeProps = ({caseId, disabled, children}) => ({
    test: !!caseId,
    Component: Button,
    onClick: () => transition(caseDetail.ROUTE, {id: caseId}),
    disabled,
    children,
});

export default connect(mapStateToProps, undefined, mergeProps)(If);

