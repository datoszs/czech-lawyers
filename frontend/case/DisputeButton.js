import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Button, Panel} from 'react-bootstrap';
import {Msg, RichText} from '../containers';
import DisputeForm from './DisputeForm';
import {isDisputed, isDisputeFormOpen, getDetail} from './selectors';
import {openDisputeForm} from './actions';

const DisputeButtonComponent = ({formOpen, disputed, openForm, final}) => {
    if (final) {
        return <Panel bsStyle="danger"><RichText msg="case.dispute.final.both" /></Panel>;
    } else if (formOpen) {
        return <DisputeForm />;
    } else {
        return <Button bsStyle="danger" disabled={disputed} onClick={openForm}><Msg msg="case.dispute" /></Button>;
    }
};

DisputeButtonComponent.propTypes = {
    formOpen: PropTypes.bool.isRequired,
    disputed: PropTypes.bool.isRequired,
    openForm: PropTypes.func.isRequired,
    final: PropTypes.bool.isRequired,
};

const mapStateToProps = (state) => {
    const detail = getDetail(state);
    return {
        formOpen: isDisputeFormOpen(state),
        disputed: isDisputed(state),
        final: !!detail && detail.advocateFinal && detail.resultFinal,
    };
};

const mapDispatchToProps = (dispatch) => ({
    openForm: () => dispatch(openDisputeForm()),
});

export default connect(mapStateToProps, mapDispatchToProps)(DisputeButtonComponent);
