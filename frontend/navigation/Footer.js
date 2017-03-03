import React from 'react';
import {Msg} from '../containers';

const Footer = () => <div>
    <hr />
    <div className="container">
        <Msg msg="copyright" />,&ensp;
        <a href="mailto:info@cestiadvokati.cz">info@cestiadvokati.cz</a>,&ensp;
        <a href="https://github.com/datoszs">github.com/datoszs</a>
    </div>
</div>;

export default Footer;
