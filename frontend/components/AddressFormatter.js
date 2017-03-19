import React, {PropTypes} from 'react';
import {Address} from '../model';

const AddressFormatter = ({value}) => (
    <p>
        {value.street}<br />
        {value.city}&emsp;{value.postcode.substring(0, 3)}&nbsp;{value.postcode.substring(3, 5)}
    </p>
);

AddressFormatter.propTypes = {
    value: PropTypes.instanceOf(Address).isRequired,
};

export default AddressFormatter;
