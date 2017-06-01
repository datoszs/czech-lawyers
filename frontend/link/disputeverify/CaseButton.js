import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Button} from 'react-bootstrap';
import {transition} from '../../util';
import translate from '../../translate';
import caseDetail from '../../case';
import {isLoading} from './selectors';

const mapStateToProps = (state) => ({
    children: translate.getMessage(state, 'case.dispute.verify.case'),
    disabled: isLoading(state),
});

const mergeProps = ({children, disabled}, dispatchProps, {id}) => ({
    onClick: () => transition(caseDetail.ROUTE, {id}),
    disabled,
    children,
});

const CaseButton = connect(mapStateToProps, undefined, mergeProps)(Button);

CaseButton.propTypes = {
    id: PropTypes.string.isRequired,
};

export default CaseButton;
