import {connect} from 'react-redux';
import {Button} from 'react-bootstrap';
import {If} from '../../util';
import translate from '../../translate';
import router from '../../router';
import {CASE_DETAIL} from '../../routes';
import {getCaseId, isLoading} from './selectors';

const mapStateToProps = (state) => ({
    caseId: getCaseId(state),
    children: translate.getMessage(state, 'case.dispute.verify.case'),
    disabled: isLoading(state),
});

const mapDispatchToProps = (dispatch) => ({
    transition: (id) => () => dispatch(router.transition(CASE_DETAIL, {id})),
});

const mergeProps = ({caseId, disabled, children}, {transition}) => ({
    test: !!caseId,
    Component: Button,
    onClick: transition(caseId),
    disabled,
    children,
});

export default connect(mapStateToProps, mapDispatchToProps, mergeProps)(If);

