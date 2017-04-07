import React, {PropTypes} from 'react';
import {connect} from 'react-redux';
import {Button} from 'react-bootstrap';
import {Msg} from '../containers';
import DisputeForm from './DisputeForm';
import {isDisputed, isDisputeFormOpen} from './selectors';
import {openDisputeForm} from './actions';

const DisputeButtonComponent = ({formOpen, disputed, openForm}) => {
    if (formOpen) {
        return <DisputeForm />;
    } else {
        return <Button bsStyle="danger" disabled={disputed} onClick={openForm}><Msg msg="case.dispute" /></Button>;
    }
};

DisputeButtonComponent.propTypes = {
    formOpen: PropTypes.bool.isRequired,
    disputed: PropTypes.bool.isRequired,
    openForm: PropTypes.func.isRequired,
};

const mapStateToProps = (state) => ({
    formOpen: isDisputeFormOpen(state),
    disputed: isDisputed(state),
});

const mapDispatchToProps = (dispatch) => ({
    openForm: () => dispatch(openDisputeForm()),
});

export default connect(mapStateToProps, mapDispatchToProps)(DisputeButtonComponent);
