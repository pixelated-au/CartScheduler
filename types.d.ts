import { route as routeFn } from 'ziggy-js';
import {Axios} from "axios";

declare global {
    const route: typeof routeFn;
    const axios: Axios;
}
