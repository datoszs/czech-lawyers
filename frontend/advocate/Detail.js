import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Alert} from 'react-bootstrap';
import {DetailField, Msg} from '../containers';
import {AdvocateDetail, statusMsg} from '../model';
import {AddressFormatter} from '../components';
import {getAdvocate} from './selectors';

const DetailComponent = ({advocate}) => (
    <div>
        <DetailField msg="advocate.ic">{advocate && advocate.ic}</DetailField>
        <DetailField msg="advocate.registration.number">{advocate && advocate.registrationNumber}</DetailField>
        <DetailField msg="advocate.status">{advocate && <Msg msg={statusMsg[advocate.status]} />}</DetailField>
        <DetailField msg="advocate.address">
            {advocate && <AddressFormatter value={advocate.address} />}
        </DetailField>
        {
            advocate && advocate.active &&
            <DetailField msg="advocate.email">
                {advocate && advocate.emails.map((email) => <div key={email}>{email}</div>)}
            </DetailField>
        }
        {advocate && !advocate.active && <Alert bsStyle="warning"><Msg msg="advocate.removed" /></Alert>}
    </div>
);

DetailComponent.propTypes = {
    advocate: PropTypes.instanceOf(AdvocateDetail),
};

DetailComponent.defaultProps = {
    advocate: null,
};

const mapStateToProps = (state) => ({
    advocate: getAdvocate(state),
});

export default connect(mapStateToProps)(DetailComponent);

