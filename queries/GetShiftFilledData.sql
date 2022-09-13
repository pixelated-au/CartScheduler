WITH RECURSIVE dates (date) AS
                   (SELECT MIN(shift_date)
                    FROM shift_user
                    WHERE shift_date >= CURRENT_DATE()
                    UNION ALL
                    SELECT date + INTERVAL 1 DAY
FROM dates
WHERE date + INTERVAL 1 DAY <= (SELECT LAST_DAY(DATE_ADD(NOW(), INTERVAL 1 MONTH))))

SELECT dates.date,
       COUNT(shift_user.id)                AS shifts_filled,
       (SELECT COUNT(shifts.id) * locations.max_volunteers
        FROM shifts
                 JOIN locations on locations.id = shifts.location_id
        WHERE CASE
                  WHEN DAYOFWEEK(dates.date) = 1 THEN shifts.day_sunday
                  WHEN DAYOFWEEK(dates.date) = 2 THEN shifts.day_monday
                  WHEN DAYOFWEEK(dates.date) = 3 THEN shifts.day_tuesday
                  WHEN DAYOFWEEK(dates.date) = 4 THEN shifts.day_wednesday
                  WHEN DAYOFWEEK(dates.date) = 5 THEN shifts.day_thursday
                  WHEN DAYOFWEEK(dates.date) = 6 THEN shifts.day_friday
                  WHEN DAYOFWEEK(dates.date) = 7 THEN shifts.day_saturday
                  END = 1
        GROUP BY locations.max_volunteers) as shifts_available
FROM dates
         LEFT JOIN shift_user ON dates.date = shift_user.shift_date
         LEFT JOIN shifts ON shifts.id = shift_user.shift_id
GROUP BY dates.date, shifts_available
ORDER BY dates.date
;
